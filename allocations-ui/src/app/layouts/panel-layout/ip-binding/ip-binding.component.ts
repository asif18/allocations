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
import { noop, Subscription } from 'rxjs';
import { finalize } from 'rxjs/operators';
import { SnackbarService, IpBindingService } from '../../../_services';
import { Title } from '@angular/platform-browser';
import * as _ from 'lodash';

@Component({
  selector: 'app-ip-binding',
  templateUrl: './ip-binding.component.html',
  styleUrls: ['./ip-binding.component.scss']
})
export class IpBindingComponent implements OnInit, OnDestroy {

  public isFormLoading: boolean = false;
  public form: FormGroup;
  private submitted = false;
  private subscriptions: Subscription[] = [];
  public cardTitle: string = 'Add New By Pass User';
  public profiles: object[] = [];

  constructor(
    private formBuilder: FormBuilder,
    private profileServie: IpBindingService,
    private snackBar: SnackbarService,
    private titleService: Title) { }

  ngOnInit(): void {
    this.titleService.setTitle('Add By Pass User');
    this.initForm();
  }

  private initForm() {
    this.form = this.formBuilder.group({
      macAddress: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      address: new FormControl(null),
      toAddress: new FormControl(null),
      type: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      comment: new FormControl(null, {
        validators: [
          Validators.required
        ]
      })
    });
  }

  get f() {
    return this.form.controls;
  }

  onSubmit() {
    this.submitted = true;

    if (this.form.invalid) {
      return;
    }

    this.isFormLoading = true;

    this.subscriptions.push(this.profileServie.saveIpBinding(this.form.value)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: any) => {
        if (response) {
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }, () => noop()));
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
