import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AccessPointManagerListComponent } from './access-point-manager-list.component';

describe('AccessPointManagerListComponent', () => {
  let component: AccessPointManagerListComponent;
  let fixture: ComponentFixture<AccessPointManagerListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AccessPointManagerListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AccessPointManagerListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
