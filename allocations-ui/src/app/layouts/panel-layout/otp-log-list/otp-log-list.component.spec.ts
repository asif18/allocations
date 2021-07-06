import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { OtpLogListComponent } from './otp-log-list.component';

describe('OtpLogListComponent', () => {
  let component: OtpLogListComponent;
  let fixture: ComponentFixture<OtpLogListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ OtpLogListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(OtpLogListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
