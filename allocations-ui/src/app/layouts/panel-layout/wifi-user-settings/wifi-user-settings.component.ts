/**
 * Copyrights Allocations 2021. All rights reserved
 *
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your
 * business/personal purpose and not to sell or modify
 *
 * Developed by: Mohamed Asif
 * Email: mohamedasif18@gmail.com
 */
import { Component, OnInit, OnDestroy } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
import { noop, Subscription, forkJoin } from 'rxjs';
import { finalize } from 'rxjs/operators';
import { Title } from '@angular/platform-browser';
import { environment } from '../../../../environments/environment';
import { DefaultApiResponse, DefaultListApiParams } from '../../../_models';
import {
  SnackbarService,
  ProfileService,
  DataUsageLimitsService,
  YardsService,
  SettingsService
  } from '../../../_services';
import * as _ from 'lodash';

interface FormDependencyData {
  profiles: object[];
  dataUsageLimits: object[];
  usageTimingLimits: object[];
  apiUrl: string;
}

interface LoginScreenSettingsData {
  profile: string;
  repeatInterval: string;
  validTill: string;
  usageTimeLimit: string;
  dataUsageLimit: string;
  canShowNameField: boolean;
  isNameFieldRequired: boolean;
  canShowEmailField: boolean;
  isEmailFieldRequired: boolean;
  advertismentImageTargetUrl: string;
  advertismentImageUrl: string;
}

@Component({
  selector: 'app-wifi-user-settings',
  templateUrl: './wifi-user-settings.component.html',
  styleUrls: ['./wifi-user-settings.component.scss']
})
export class WifiUserSettingsComponent implements OnInit, OnDestroy {

  public isFormLoading: boolean = false;
  public form: FormGroup;
  private submitted = false;
  private subscriptions: Subscription[] = [];
  public loginScreenSettingsData: LoginScreenSettingsData = {
    profile: null,
    repeatInterval: null,
    validTill: null,
    usageTimeLimit: null,
    dataUsageLimit: null,
    canShowNameField: false,
    isNameFieldRequired: false,
    canShowEmailField: false,
    isEmailFieldRequired: false,
    advertismentImageTargetUrl: null,
    advertismentImageUrl: null
  };
  public formDependencyData: FormDependencyData = {
    profiles: [],
    dataUsageLimits: [],
    usageTimingLimits: [],
    apiUrl: environment.apiUrl
  };

  constructor(
    private formBuilder: FormBuilder,
    private profileService: ProfileService,
    private dataUsageLimitsService: DataUsageLimitsService,
    private usageTimingLimitsService: YardsService,
    private settingsService: SettingsService,
    private snackBar: SnackbarService,
    private titleService: Title) {}

  ngOnInit(): void {
    this.titleService.setTitle('Wifi User Settings');
    this.initForm();
    this.getFormInitialData();
  }

  private getFormInitialData(): void {

    this.isFormLoading = true;
    const params: DefaultListApiParams = {
      searchBy: null,
      startFrom: null,
      endTo: null,
      sortBy: 'size',
      sortDirection: 'desc',
    };
    const requests = forkJoin(
      this.profileService.getProfiles(),
      this.dataUsageLimitsService.getDataUsageLimits(params),
      this.usageTimingLimitsService.getUsageTimingLimits(_.merge(_.clone(params), {sortBy: 'time'})),
      this.settingsService.getWifiUserSettings()
    );

    this.subscriptions.push(requests
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: Array<DefaultApiResponse>) => {

        _.assign(this.formDependencyData, {
          profiles: response[0].data.items,
          dataUsageLimits: response[1].data.items,
          usageTimingLimits: _.filter(response[2].data.items, (item) => (item.time === 'Days' || item.time === 'Day'))
        });

        if (response[3]) {
          this.loginScreenSettingsData = response[3].data;
          this.form.patchValue({
            profile: this.loginScreenSettingsData.profile,
            repeatInterval: this.loginScreenSettingsData.repeatInterval,
            validTill: this.loginScreenSettingsData.validTill,
            usageTimeLimit: this.loginScreenSettingsData.usageTimeLimit,
            dataUsageLimit: this.loginScreenSettingsData.dataUsageLimit,
            canShowNameField: this.loginScreenSettingsData.canShowNameField,
            isNameFieldRequired: this.loginScreenSettingsData.isNameFieldRequired,
            canShowEmailField: this.loginScreenSettingsData.canShowEmailField,
            isEmailFieldRequired: this.loginScreenSettingsData.isEmailFieldRequired,
            advertismentImageTargetUrl: this.loginScreenSettingsData.advertismentImageTargetUrl
          });
        }
      }));
  }

  private initForm(): void {
    this.form = this.formBuilder.group({
      profile: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      repeatInterval: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      validTill: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      usageTimeLimit: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      dataUsageLimit: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      canShowNameField: new FormControl(false),
      isNameFieldRequired: new FormControl(false),
      canShowEmailField: new FormControl(false),
      isEmailFieldRequired: new FormControl(false),
      advertismentImageTargetUrl: new FormControl(null)
    });
  }

  public uploadImage(imageInput: any): void {
    const file: File = imageInput.files[0];
    this.isFormLoading = true;
    this.subscriptions.push(this.settingsService.uploadAdvertismentImage(file)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        this.loginScreenSettingsData.advertismentImageUrl = response.data.advertismentImageUrl;
      }));
  }

  public removeAdvertismentImage(): void {
    this.isFormLoading = true;
    this.subscriptions.push(this.settingsService.removeAdvertismentImage()
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        this.loginScreenSettingsData.advertismentImageUrl = null;
      }));
  }

  get f() {
    return this.form.controls;
  }

  onSubmit(): void {
    this.submitted = true;

    if (this.form.invalid) {
      return;
    }

    this.isFormLoading = true;
    this.settingsService.saveWifiUserSettings(this.form.value)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => this.snackBar.show(response.message, response.status ? 'success' : 'danger'),
      () => noop());
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
